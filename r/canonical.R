###########################################################################
# evaluation-script.R                                                     #
#                                                                         #
# Comparing Anomaly Detectors for Keystroke Biometrics                    #
# Evaluation Proceedure                                                   #
# R Script                                                                #
#                                                                         #
# by: Kevin Killourhy                                                     #
# date: May 19, 2009                                                      #
###########################################################################

# This script demonstrates how the DSL-StrongPassword data set can be
# used to train and evaluate three anomaly detectors, reproducing the
# results for those anomaly detectors reported in:

#   K.S. Killourhy and R.A. Maxion. "Comparing Anomaly Detectors for
#   Keystroke Biometrics," in Proceedings of the 39th Annual
#   Dependable Systems and Networks Conference, pages 125-134,
#   Estoril, Lisbon, Portugal, June 29-July 2, 2009.  IEEE Computer
#   Society Press, Los Alamitos, California. 2009.

# The script is shared in order to promote open scientific discourse,
# and to encourage other researchers to reproduce and expand upon our
# results (e.g., by evaluating their own, novel anomaly detectors)
# using our data and procedures.

###########################################################################
# Table of Contents
###########################################################################

# 1. Anomaly detectors
# 2. Evaluation functions
# 3. Main procedure

library( MASS );
library( ROCR );
library( stats );

###########################################################################
# 1. Anomaly detectors
###########################################################################

# The euclideanTrain and euclideanScore functions comprise the
# Euclidean anomaly detector.  During training, the detector takes a
# set of password-timing vectors (encoded as rows in a matrix) and
# calculates the mean vector.  This mean vector is returned as the
# detection model.  During scoring, the detector takes the detection
# model and a new set of password-timing vectors (also encoded as rows
# in a matrix) and calculates the squared Euclidean distance between
# the mean vector and each of the new password-timing vectors.  These
# scores are returned in a vector whose length is equal to the number
# of password-timing vectors in the scoring matrix.

euclideanTrain <- function( YTrain ) {
  # dmod --> "Detection model"
  dmod <- list ( mean  = colMeans( YTrain ) );
  return( dmod );
}

euclideanScore <- function( dmod, YScore ) {
  p <- length( dmod$mean );
  n <- nrow( YScore );

  if( ncol(YScore) != p ) stop("Training/test feature length mismatch ");
  
  meanMatrix <- matrix( dmod$mean, byrow=TRUE, nrow=n, ncol=p );

  scores <- rowSums( ( YScore - meanMatrix )^2 );

  return( scores );
}

# The manhattanTrain and manhattanScore functions comprise the
# Manhattan anomaly detector.  During training, the detector takes a
# set of password-timing vectors (encoded as rows in a matrix) and
# calculates the mean vector.  This mean vector is returned as the
# detection model.  During scoring, the detector takes the detection
# model and a new set of password-timing vectors (also encoded as rows
# in a matrix) and calculates the Manhattan distance between the mean
# vector and each of the new password-timing vectors.  These scores
# are returned in a vector whose length is equal to the number of
# password-timing vectors in the scoring matrix.

manhattanTrain <- function( YTrain ) {
  dmod <- list ( mean  = colMeans( YTrain ) );
  return( dmod );
}

manhattanScore <- function( dmod, YScore ) {
  p <- length( dmod$mean );
  n <- nrow( YScore );

  if( ncol(YScore) != p ) stop("Training/test feature length mismatch ");
  
  meanMatrix <- matrix( dmod$mean, byrow=TRUE, nrow=n, ncol=p );

  scores <- rowSums( abs( YScore - meanMatrix ) );

  return( scores );
}

# The mahalanobisTrain and mahalanobisScore functions comprise the
# Mahalanobis anomaly detector.  During training, the detector takes a
# set of password-timing vectors (encoded as rows in a matrix) and
# calculates the mean vector and also the inverse of the covariance
# matrix.  This vector and matrix are returned as the detection model.
# During scoring, the detector takes the detection model and a new set
# of password-timing vectors (also encoded as rows in a matrix) and
# calculates the squared Mahalanobis distance between the mean vector
# and each of the new password-timing vectors.  These scores are
# returned in a vector whose length is equal to the number of
# password-timing vectors in the scoring matrix.

mahalanobisTrain <- function( YTrain ) {
  dmod <- list( mean  = colMeans( YTrain ),
               covInv = ginv( cov( YTrain ) ) );
  return( dmod );
}

mahalanobisScore <- function( dmod, YScore ) {
  p <- length( dmod$mean );
  n <- nrow( YScore );

  if( ncol(YScore) != p ) stop("Training/test feature length mismatch ");
  
  scores <- mahalanobis( YScore, dmod$mean, dmod$covInv, inverted=TRUE );

  return( scores );
}

# The detectorSet data structure compiles the train and score
# functions for each detector into a named list, with the name
# corresponding to the name of the detector.

detectorSet = list(
  Euclidean =
  list( train = euclideanTrain,
       score = euclideanScore ),
  Manhattan =
  list( train = manhattanTrain,
       score = manhattanScore ),
  Mahalanobis =
  list( train = mahalanobisTrain,
       score = mahalanobisScore ) );


evaluate.their.data <- function( X, evalSubject, detectorTrain, detectorScore ) {
  print(summary(X))
    YTrain <- as.matrix( subset( X,
                              # subset: logical expression indicating
                              # elements or rows to keep: missing values
                              # are taken as false.
                              subset = ( subject == evalSubject &
                                        sessionIndex == 2 & rep <= 15 ),
                              # select: expression, indicating columns
                              # to select from a data frame.
                              #   exclude subj., session num, and rep cols
                              select = -c( subject, sessionIndex, rep ) ) );
  YScore0 <- as.matrix( subset( X,
                               subset = ( subject == evalSubject &
                                         sessionIndex == 5 & rep == 1 ),
                               select = -c( subject, sessionIndex, rep ) ) );


  # Run the training and scoring procedures on the appropriate matrices
  # to obtain the user and impostor scores.

  dmod <- detectorTrain( YTrain );
  userScores <- detectorScore( dmod, YScore0 );

  # Use the user and impostor scores to calculate the detector's equal
  # error rate for the evaluation subject.
  return(userScores);
}

# The evaluateSubject function takes a password-timing data frame, an
# ID for one of the subjects, and an anomaly detector's training and
# scoring functions.  It performs the training/scoring procedure for
# that subject, extracting the appropriate password-timing matrices,
# running the training and scoring functions, and then running the
# analysis to calculate the equal-error rates.  The equal-error rate
# for the subject is returned.

evaluateSubject <- function( X, Y, detectorTrain, detectorScore ) {

  # Extract the training, user scoring, and impostor scoring matricies
  # for the subject.  The training matrix is the first 200 password
  # repetitions for the subject, corresponding to the first 4 sessions
  # of passwords.  The user scoring matrix is the last 200 password
  # repetitions, and the impostor scoring matrix is the first 5
  # repetitions from all the other subjects.

  YTrain <- as.matrix( subset( X,
                              # subset: logical expression indicating
                              # elements or rows to keep: missing values
                              # are taken as false.
                              subset = TRUE,
                              # select: expression, indicating columns
                              # to select from a data frame.
                              #   exclude subj., session num, and rep cols
                              select = -c( repetition ) ) );

length.with.Enters <- length( X );
length.new <- length.with.Enters - 2
YTrain <- YTrain[,(1:length.new)]
  
  YScore0 <- as.matrix( Y );
  YScore0 <- YScore0[,(1:length.new)]

  print(YTrain)
print(YScore0)
  
  print(YScore0)


  # Run the training and scoring procedures on the appropriate matrices
  # to obtain the user and impostor scores.

  dmod <- detectorTrain( YTrain );
  userScores <- detectorScore( dmod, YScore0 );

  # Use the user and impostor scores to calculate the detector's equal
  # error rate for the evaluation subject.
  return(userScores);
}



###########################################################################
# 3. Main procedure
###########################################################################

#######################
cat("Loading the data file\n");

try.my.data <- TRUE;

if( try.my.data ) {
  datafile <- 'training_data.csv';
  if( ! file.exists(datafile) )
    stop( "Password data file ",datafile," does not exist");

  current <- 'current_attempt.csv';
  if( ! file.exists(current) )
    stop( "Current attempt data file ",current," does not exist");

  # Retrieve the data and the list of subjects
  
  X <- read.csv( datafile, header = TRUE );
  Y <- read.csv( current, header = TRUE );

  # For each of the detectors, evaluate the detector on each subject,
  # and record the equal-error rates in a data frame.
  print( evaluateSubject(X, Y, mahalanobisTrain, mahalanobisScore ) );
} else {
  datafile <- 'few_examples.txt';
  if( ! file.exists(datafile) )
    stop( "Password data file ",datafile," does not exist");

  # Retrieve the data and the list of subjects
  
  X <- read.table( datafile, header = TRUE );

  print( evaluate.their.data( X, "s005", mahalanobisTrain, mahalanobisScore ) );
}


