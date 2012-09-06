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
  dmod <- list ( mean  = colMeans( YTrain ) );
  return( dmod );
}


# The evaluateSubject function takes a password-timing data frame, an
# ID for one of the subjects, and an anomaly detector's training and
# scoring functions.  It performs the training/scoring procedure for
# that subject, extracting the appropriate password-timing matrices,
# running the training and scoring functions, and then running the
# analysis to calculate the equal-error rates.  The equal-error rate
# for the subject is returned.

trainSubject <- function( X, evalSubject, detectorTrain, detectorScore ) {

  # Extract the training, user scoring, and impostor scoring matricies
  # for the subject.  The training matrix is the first 200 password
  # repetitions for the subject, corresponding to the first 4 sessions
  # of passwords.  The user scoring matrix is the last 200 password
  # repetitions, and the impostor scoring matrix is the first 5
  # repetitions from all the other subjects.

  YTrain <- as.matrix( subset( X,
                              subset = ( subject == evalSubject &
                                        sessionIndex <= 4 ),
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

  # Use the user and impostor scores to calculate the detector's equal
  # error rate for the evaluation subject.

  eer <- calculateEqualError( userScores, impostorScores );

  return( eer );  
}


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
