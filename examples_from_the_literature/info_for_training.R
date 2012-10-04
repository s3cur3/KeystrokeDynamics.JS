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
  Mahalanobis =
  list( train = mahalanobisTrain,
       score = mahalanobisScore ) );

###########################################################################
# 2. Evaluation procedures
###########################################################################

# The calculateEqualError function takes a set of user scores and
# impostor scores, makes an ROC curve using the ROCR functionality,
# and then geometrically calculates the point at which the miss and
# false-alarm (i.e., false-negative and false-positive) rates are
# equal.

calculateEqualError <- function( userScores, impostorScores ) {

  predictions <- c( userScores, impostorScores );
  labels <- c( rep( 0, length( userScores ) ),
              rep( 1, length( impostorScores ) ) );
  
  pred <- prediction( predictions, labels );

  missrates <- pred@fn[[1]] / pred@n.pos[[1]];
  farates <- pred@fp[[1]] / pred@n.neg[[1]];

  # Find the point on the ROC with miss slightly >= fa, and the point
  # next to it with miss slightly < fa.
  
  dists <- missrates - farates;
  idx1 <- which( dists == min( dists[ dists >= 0 ] ) );
  idx2 <- which( dists == max( dists[ dists < 0 ] ) );
  stopifnot( length( idx1 ) == 1 );
  stopifnot( length( idx2 ) == 1 );
  stopifnot( abs( idx1 - idx2 ) == 1 );

  # Extract the two points as (x) and (y), and find the point on the
  # line between x and y where the first and second elements of the
  # vector are equal.  Specifically, the line through x and y is:
  #   x + a*(y-x) for all a, and we want a such that
  #   x[1] + a*(y[1]-x[1]) = x[2] + a*(y[2]-x[2]) so
  #   a = (x[1] - x[2]) / (y[2]-x[2]-y[1]+x[1])
  
  x <- c( missrates[idx1], farates[idx1] );
  y <- c( missrates[idx2], farates[idx2] );
  a <- ( x[1] - x[2] ) / ( y[2] - x[2] - y[1] + x[1] );
  eer <- x[1] + a * ( y[1] - x[1] );

  return( eer );
}


# The evaluateSubject function takes a password-timing data frame, an
# ID for one of the subjects, and an anomaly detector's training and
# scoring functions.  It performs the training/scoring procedure for
# that subject, extracting the appropriate password-timing matrices,
# running the training and scoring functions, and then running the
# analysis to calculate the equal-error rates.  The equal-error rate
# for the subject is returned.

evaluateSubject <- function( X, evalSubject, detectorTrain, detectorScore ) {

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
                              subset = ( subject == evalSubject &
                                        sessionIndex <= 1 & rep <= 15 ),
                              # select: expression, indicating columns
                              # to select from a data frame.
                              #   exclude subj., session num, and rep cols
                              select = -c( subject, sessionIndex, rep ) ) );

  YScore0 <- as.matrix( subset( X,
                               subset = ( subject == evalSubject &
                                         sessionIndex > 4 ),
                               select = -c( subject, sessionIndex, rep ) ) );

  YScore1 <- as.matrix( subset( X,
                               subset = ( subject != evalSubject &
                                         sessionIndex == 1 &
                                         rep <= 5 ),
                               select = -c( subject, sessionIndex, rep ) ) );

  # Run the training and scoring procedures on the appropriate matrices
  # to obtain the user and impostor scores.

  dmod <- detectorTrain( YTrain );
  userScores <- detectorScore( dmod, YScore0 );
  impostorScores <- detectorScore( dmod, YScore1 );
  print("User scores: ");
  print(userScores);
  print("Imposter scores: ");
  print(impostorScores);

  # Use the user and impostor scores to calculate the detector's equal
  # error rate for the evaluation subject.

  eer <- calculateEqualError( userScores, impostorScores );

  return( eer );  
}



###########################################################################
# 3. Main procedure
###########################################################################

#######################
cat("Loading the data file\n");

datafile <- 'few_examples.txt';
if( ! file.exists(datafile) )
  stop( "Password data file ",datafile," does not exist");

# Retrieve the data and the list of subjects
  
X <- read.table( datafile, header = TRUE );
subjects <- sort( levels( X$subject ) );

# For each of the detectors, evaluate the detector on each subject,
# and record the equal-error rates in a data frame.

eers <- list();
unlink("out.txt"); # delete the old output file
for( detectorName in names( detectorSet ) ) {

  #######################
  cat("Evaluating the",detectorName,"detector\n");
  detectorTrain = detectorSet[[ detectorName ]]$train;
  detectorScore = detectorSet[[ detectorName ]]$score;
      
  eers[[ detectorName ]] <- rep( NA, length(subjects) );

  n <- length(subjects);
  for( i in 1:n ) {

    eer <- evaluateSubject( X, subjects[i],
                             detectorTrain = detectorTrain,
                             detectorScore = detectorScore );

    eers[[ detectorName ]][i] <- eer;
    cat("\r  ",i,"/",n,":",eer);
  }
  cat("\r  average equal-error:",mean(eers[[detectorName]]),"\n");
  write("average equal-error:", file="out.txt", append=TRUE);
  write(mean(eers[[detectorName]]), file="out.txt", append=TRUE);
}

#######################
# Send all output to file out.txt
sink(file = "out.txt", append = TRUE, type = c("output", "message"),
     split = FALSE)
cat("Tabulating results:\n");

eers <- data.frame( eers );
rownames( eers ) <- subjects;

res <- data.frame(eer.mean = colMeans(eers),
                  eer.sd   = apply( eers, 2, sd ));

print( round( res, digits = 3 ) );
